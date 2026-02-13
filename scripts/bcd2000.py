#!/usr/bin/env python3
"""
Bixolon BCD-2000K Customer Display driver via ftdi_sio serial.
Usage: bcd2000.py <command> [args...]
  clear              - Clear display
  welcome <company>  - Show welcome + company + date
  cart <name> <qty> <price> - Show cart item
  total <customer> <amount> - Show total
  text <line1> [line2]      - Show custom text
"""
import sys
import os
import time
from datetime import datetime

DEVICE = '/dev/ttyUSB0'

def pad(text, width=20):
    """Pad/truncate text to exact width."""
    encoded = text.encode('ascii', 'replace')[:width]
    return encoded.ljust(width)

def send(data):
    """Send bytes to display via serial device."""
    fd = os.open(DEVICE, os.O_WRONLY | os.O_NOCTTY | os.O_NONBLOCK)
    os.write(fd, data)
    os.close(fd)

def main():
    if len(sys.argv) < 2:
        print("Usage: bcd2000.py <command> [args]", file=sys.stderr)
        sys.exit(1)

    if not os.path.exists(DEVICE):
        print("ERR:NO_DEVICE", file=sys.stderr)
        sys.exit(1)

    cmd = sys.argv[1]

    if cmd == 'clear':
        send(b'\x1b\x40\x0c')

    elif cmd == 'welcome':
        company = sys.argv[2] if len(sys.argv) > 2 else ''
        date_str = datetime.now().strftime('%d/%m/%Y')
        buf  = b'\x1b\x40'          # ESC @ init
        buf += b'\x0c'               # clear
        buf += pad('Bienvenue')
        buf += b'\x1f\x24\x01\x02'  # cursor line 2
        buf += pad(company[:10] + ' ' + date_str)
        send(buf)

    elif cmd == 'cart':
        name  = sys.argv[2] if len(sys.argv) > 2 else ''
        qty   = sys.argv[3] if len(sys.argv) > 3 else ''
        price = sys.argv[4] if len(sys.argv) > 4 else ''
        buf  = b'\x1b\x40\x0c'
        buf += pad(name)
        buf += b'\x1f\x24\x01\x02'
        buf += pad('Qte:' + qty + ' Px:' + price)
        send(buf)

    elif cmd == 'total':
        customer = sys.argv[2] if len(sys.argv) > 2 else ''
        amount   = sys.argv[3] if len(sys.argv) > 3 else ''
        buf  = b'\x1b\x40\x0c'
        buf += pad(customer)
        buf += b'\x1f\x24\x01\x02'
        buf += pad('Total TTC ' + amount)
        send(buf)

    elif cmd == 'text':
        l1 = sys.argv[2] if len(sys.argv) > 2 else ''
        l2 = sys.argv[3] if len(sys.argv) > 3 else ''
        buf  = b'\x1b\x40\x0c'
        buf += pad(l1)
        if l2:
            buf += b'\x1f\x24\x01\x02'
            buf += pad(l2)
        send(buf)

    else:
        print("ERR:UNKNOWN_CMD", file=sys.stderr)
        sys.exit(1)

    print("OK")

if __name__ == '__main__':
    main()
